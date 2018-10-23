<?php
namespace App\Admin;

use App\Entity\Company;
use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Content', ['class' => 'col-md-9'])
                ->add('firstname', TextType::class)
                ->add('lastname', TextType::class)
                ->add('email', TextType::class)
            ->end()
            ->with('Meta data', ['class' => 'col-md-3'])
                ->add('company', ModelType::class, [
                    'class' => Company::class,
                    'property' => 'name',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('firstname')
            ->add('email')
            ->add('company', null, [], EntityType::class, [
                'class'    => Company::class,
                'choice_label' => 'name',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('firstname')
            ->add('lastname')
            ->add('email')
            ->add('company.name')
        ;
    }

    /**
     * To customise messages when user is created or modified.
     * This will override the default hash message displayed
     * @param $object
     * @return string
     */
    public function toString($object)
    {
        return $object instanceof User
            ? $object->getFirstName() . " " . $object->getLastName()
            : 'User'; // shown in the breadcrumb on the create view
    }
}